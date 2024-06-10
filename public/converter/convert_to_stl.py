import sys
import cadquery as cq
from OCP.IGESControl import IGESControl_Reader
from OCP.IFSelect import IFSelect_RetDone
from OCP.STEPControl import STEPControl_Reader
from OCP.BRepMesh import BRepMesh_IncrementalMesh
from OCP.TopoDS import TopoDS_Compound, TopoDS_Builder
from OCP.BRep import BRep_Builder

def generate_fine_mesh(shape, linear_deflection=0.01, angular_deflection=0.1):
    """
    Generates a fine mesh for the given shape with custom parameters.
    """
    mesh = BRepMesh_IncrementalMesh(shape, linear_deflection, True, angular_deflection, True)
    mesh.Perform()
    return mesh

def convert_step_to_stl(input_file, output_file, tolerance=0.01):
    try:
        model = cq.importers.importStep(input_file)
        cq.exporters.export(model, output_file, tolerance=tolerance)
        print(f"Successfully converted {input_file} to {output_file}")
    except Exception as e:
        print("The command that was run:", " ".join(sys.argv))
        print(f"Error converting STEP file: {e}")

def convert_igs_to_stl(input_file, output_file, tolerance=0.01):
    try:
        iges_reader = IGESControl_Reader()
        status = iges_reader.ReadFile(input_file)

        if status != IFSelect_RetDone:
            print(f"Error: Unable to read the IGES file {input_file}")
            return

        iges_reader.TransferRoots()

        builder = TopoDS_Builder()
        compound = TopoDS_Compound()
        builder.MakeCompound(compound)

        for i in range(1, iges_reader.NbShapes() + 1):
            shape = iges_reader.Shape(i)
            builder.Add(compound, shape)

        generate_fine_mesh(compound)  # Generate finer mesh

        model = cq.Compound.makeCompound([cq.Shape.cast(compound)])
        cq.exporters.export(model, output_file, tolerance=tolerance)
        print(f"Successfully converted {input_file} to {output_file}")
    except Exception as e:
        print("The command that was run:", " ".join(sys.argv))
        print(f"Error converting IGS file: {e}")

if __name__ == "__main__":
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    file_extension = input_file.split('.')[-1].upper()

    if file_extension == 'STEP':
        convert_step_to_stl(input_file, output_file)
    elif file_extension == 'IGS':
        convert_igs_to_stl(input_file, output_file)
    else:
        print(f"Unsupported file extension: {file_extension}")
